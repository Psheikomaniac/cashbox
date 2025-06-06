<?php

namespace App\Form;

use App\DTO\PenaltyInputDTO;
use App\Entity\PenaltyType as PenaltyTypeEntity;
use App\Entity\Team;
use App\Entity\User;
use App\Enum\CurrencyEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PenaltyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamId', EntityType::class, [
                'label' => 'Team',
                'class' => Team::class,
                'choice_label' => 'name',
                'choice_value' => function (?Team $team) {
                    return $team ? $team->getId()->toString() : '';
                },
                'placeholder' => 'Select a team',
                'required' => true,
            ])
            ->add('userId', EntityType::class, [
                'label' => 'User',
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName()->getFullName();
                },
                'choice_value' => function (?User $user) {
                    return $user ? $user->getId()->toString() : '';
                },
                'placeholder' => 'Select a user',
                'required' => true,
            ])
            ->add('typeId', EntityType::class, [
                'label' => 'Penalty Type',
                'class' => PenaltyTypeEntity::class,
                'choice_label' => 'name',
                'choice_value' => function (?PenaltyTypeEntity $penaltyType) {
                    return $penaltyType ? $penaltyType->getId()->toString() : '';
                },
                'placeholder' => 'Select a penalty type',
                'required' => true,
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Reason',
                'required' => true,
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                'currency' => false,
                'divisor' => 100,
                'html5' => true,
                'required' => true,
            ])
            ->add('currency', ChoiceType::class, [
                'label' => 'Currency',
                'choices' => array_combine(
                    array_map(fn(CurrencyEnum $currency) => $currency->getSymbol() . ' ' . $currency->value, CurrencyEnum::cases()),
                    array_map(fn(CurrencyEnum $currency) => $currency->value, CurrencyEnum::cases())
                ),
                'placeholder' => 'Select currency',
                'required' => true,
            ])
            ->add('archived', CheckboxType::class, [
                'label' => 'Archived',
                'required' => false,
            ])
            ->add('paidAt', DateTimeType::class, [
                'label' => 'Paid At',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PenaltyInputDTO::class,
            'csrf_protection' => true,
        ]);
    }
}
